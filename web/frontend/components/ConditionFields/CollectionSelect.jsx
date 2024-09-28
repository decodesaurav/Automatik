import { InlineStack, RadioButton, Select, TextField,Text, BlockStack } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useEffect, useState } from "react";
import { useAuthenticatedFetch } from "@shopify/app-bridge-react";

export default function CollectionSelect({state,dispatch}) {
  const { t } = useTranslation();
  const [collections,setCollections] = useState([]);
  const fetch = useAuthenticatedFetch();

  useEffect(() => {
    fetchCollections();
  },[]);

  const handleCollectionChange = (value) => {
    dispatch({
      type: 'HANDLE_CONDITION_CHANGE',
      payload: { field: 'collection', data: { method: 'collection' } },
    });
    dispatch({
      type: 'HANDLE_CONDITION_CHANGE',
      payload: { field: 'collection', data: { value: value } },
    });
  };

  const condition = state.conditions.find(cond => cond.field === 'collection') || {};
  const errorData = state.errorData?.conditions?.['collection'] || {};

  const fetchCollections = () => {
    const response = fetch("/api/collections", {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
        }
    })
    .then((response) => {
        if (!response.ok) {
            return "not ok";
        }
        return response.json();

    }).then((response) => {
        setCollections(response.collections)
    })
    .catch((error) => {
        console.log(error);
    });
};

const formattedCollections = () => {
    return collections?.map((coll) => ({
      label: coll.title,
      value: coll.id,
    }));
  };
  

  console.log(formattedCollections(),"for collections")

  return (
    <>
        <BlockStack gap={100}>
            <Text>Where product is in Collection</Text>
            <InlineStack gap={200}>
                    <Select
                        options={formattedCollections()}
                        onChange={handleCollectionChange}
                        value={parseInt(condition?.value)}
                    />
                </InlineStack>
        </BlockStack>
    </>
  );

}
