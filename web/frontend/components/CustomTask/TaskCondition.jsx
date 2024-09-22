import { Tag, Autocomplete, LegacyStack,Text, BlockStack} from '@shopify/polaris';
import {useState, useCallback, useMemo, useReducer} from 'react';
import PriceField from '../ConditionFields/PriceField';
import StockField from '../ConditionFields/StockField';
import TitleField from '../ConditionFields/TitleField';
import CollectionSelect from '../ConditionFields/CollectionSelect';
import CustomTaskReducer, { customeTaskState } from '../../reducers/CustomTaskReducer';

export default function TaskCondition({selectedTask}) {
  const [state, dispatch] = useReducer(CustomTaskReducer, customeTaskState);
  const deselectedOptions = [
      {value: 'collection', label: 'Collection'},
      {value: 'price', label: 'Price'},
      {value: 'stock', label: 'Stock'},
      {value: 'title', label: 'Title'},
      {value: 'vendor', label: 'Vendor'},
    ];
  const [selectedOptions, setSelectedOptions] = useState(['price']);
  const [inputValue, setInputValue] = useState('');
  const [options, setOptions] = useState(deselectedOptions);

  const updateText = (value) => {
      setInputValue(value);

      if (value === '') {
        setOptions(deselectedOptions);
        return;
      }

      const filterRegex = new RegExp(value, 'i');
      const resultOptions = deselectedOptions.filter((option) =>
        option.label.match(filterRegex),
      );

      setOptions(resultOptions);
    };

  const removeTag = useCallback(
    (tag) => () => {
      const options = [...selectedOptions];
      options.splice(options.indexOf(tag), 1);
      setSelectedOptions(options);
      dispatch({ type: 'REMOVE_CONDITION', payload: { field: tag } });
    },
    [selectedOptions],
  );

  const verticalContentMarkup =
    selectedOptions.length > 0 ? (
      <LegacyStack spacing="extraTight" alignment="center">
        {selectedOptions.map((option) => {
          let tagLabel = '';
          tagLabel = option.replace('_', ' ');
          tagLabel = titleCase(tagLabel);
          return (
            <Tag key={`option${option}`} onRemove={removeTag(option)}>
              {tagLabel}
            </Tag>
          );
        })}
      </LegacyStack>
    ) : null;

  const textField = (
    <Autocomplete.TextField
      onChange={updateText}
      label="Choose option(s) to apply condition using that field."
      value={inputValue}
      verticalContent={verticalContentMarkup}
      autoComplete="off"
    />
  );

  return (
    <>
    <div>
      <Autocomplete
        allowMultiple
        options={options}
        selected={selectedOptions}
        textField={textField}
        onSelect={setSelectedOptions}
        listTitle="Suggested Tags"
      />
    </div>

    <BlockStack gap={400}>
        {
            selectedOptions.map(option => {
                if(option =='price'){
                    return <PriceField state={state} dispatch={dispatch}/>
                }
                if(option =='stock'){
                    return <StockField state={state} dispatch={dispatch}/>
                }
                if(option == 'title'){
                    return <TitleField  state={state} dispatch={dispatch}/>
                }
                if(option == 'collection'){
                    return <CollectionSelect state={state} dispatch={dispatch}/>
                }
            })
        }
    </BlockStack>
    </>
  );

  function titleCase(string) {
    return string
      .toLowerCase()
      .split(' ')
      .map((word) => word.replace(word[0], word[0].toUpperCase()))
      .join('');
  }
}