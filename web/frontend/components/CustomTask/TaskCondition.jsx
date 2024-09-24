import { Tag, Autocomplete, LegacyStack, BlockStack, InlineError } from '@shopify/polaris';
import { useState, useCallback } from 'react';
import PriceField from '../ConditionFields/PriceField';
import StockField from '../ConditionFields/StockField';
import TitleField from '../ConditionFields/TitleField';
import CollectionSelect from '../ConditionFields/CollectionSelect';

export default function TaskCondition({ selectedTask, stateData, dispatch, actionTypes, errorData }) {
  let state = stateData;
  const deselectedOptions = [
    { value: 'collection', label: 'Collection' },
    { value: 'price', label: 'Price' },
    { value: 'stock', label: 'Stock' },
    { value: 'title', label: 'Title' },
    { value: 'vendor', label: 'Vendor' },
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

  const handleSelect = (newSelectedOptions) => {
    setSelectedOptions(newSelectedOptions);
    dispatch({ type: actionTypes.SET_SELECTED_OPTIONS, payload: newSelectedOptions });
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
          let tagLabel = option.replace('_', ' ');
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
        <BlockStack>
          <Autocomplete
            allowMultiple
            options={options}
            selected={selectedOptions}
            textField={textField}
            onSelect={handleSelect}
            listTitle="Suggested Tags"
          />
          {state?.errorData.conditionsOptions && (
            <InlineError message={state?.errorData.conditionsOptions} fieldID="conditionsOptions" />
          )}
        </BlockStack>
      </div>

      <BlockStack gap={400}>
        {selectedOptions.map(option => {
          if (option === 'price') {
            return <PriceField state={state} dispatch={dispatch} key="price" />;
          }
          if (option === 'stock') {
            return <StockField state={state} dispatch={dispatch} key="stock" />;
          }
          if (option === 'title') {
            return <TitleField state={state} dispatch={dispatch} key="title" />;
          }
          if (option === 'collection') {
            return <CollectionSelect state={state} dispatch={dispatch} key="collection" />;
          }
          return null;
        })}
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
